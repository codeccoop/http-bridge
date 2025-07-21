// source
import useBackendNames from "../../hooks/useBackendNames";
import BackendHeaders from "./Headers";
import { downloadJson, validateUrl, validateBackend } from "../../lib/utils";
import useResponsive from "../../hooks/useResponsive";
import BackendFields from "./Fields";

const { Button } = wp.components;
const { useState, useEffect, useMemo, useRef } = wp.element;
const { __ } = wp.i18n;

export default function Backend({ update, remove, data, copy }) {
  const isResponsive = useResponsive();

  const name = useRef(data.name);
  const [state, setState] = useState({ ...data });

  const names = useBackendNames();

  const nameConflict = useMemo(() => {
    if (!state.name) return false;
    if (state.name.trim() === name.current.trim()) return false;
    return state.name !== name.current && names.has(state.name.trim());
  }, [names, state.name]);

  const invalidUrl = useMemo(() => {
    return !validateUrl(state.base_url, true);
  }, [state.base_url]);

  const isValid = useMemo(
    () => !nameConflict && !invalidUrl && validateBackend(state),
    [state, nameConflict, invalidUrl]
  );

  const timeout = useRef();
  useEffect(() => {
    clearTimeout(timeout.current);

    if (isValid) {
      timeout.current = setTimeout(
        () => {
          name.current = state.name;
          update(state);
        },
        (data.name !== state.name && 1e3) || 0
      );
    }
  }, [isValid, state]);

  useEffect(() => {
    if (data.name !== name.current) {
      name.current = data.name;
      setState(data);
    }
  }, [data.name]);

  function exportConfig() {
    const backendData = { ...data };
    downloadJson(backendData, data.name + " backend config");
  }

  return (
    <div
      style={{
        padding: "calc(24px) calc(32px)",
        width: "calc(100% - 64px)",
        backgroundColor: "rgb(245, 245, 245)",
        display: "flex",
        flexDirection: isResponsive ? "column" : "row",
        gap: "2rem",
      }}
    >
      <div style={{ display: "flex", flexDirection: "column", gap: "0.5rem" }}>
        <BackendFields
          state={state}
          setState={setState}
          errors={{
            name: nameConflict,
            base_url: invalidUrl,
          }}
        />
        <div
          style={{
            marginTop: "0.5rem",
            display: "flex",
            gap: "0.5rem",
          }}
        >
          <Button
            variant="primary"
            onClick={() => remove(data)}
            style={{
              height: "40px",
              width: "40px",
              justifyContent: "center",
              fontSize: "1.5em",
              border: "1px solid",
              padding: "6px 6px",
            }}
            label={__("Delete", "forms-bridge")}
            showTooltip
            isDestructive
            __next40pxDefaultSize
          >
            <BinIcon width="12" height="20" />
          </Button>
          <Button
            variant="tertiary"
            style={{
              height: "40px",
              width: "40px",
              justifyContent: "center",
              fontSize: "1.5em",
              border: "1px solid",
              padding: "6px 6px",
            }}
            onClick={copy}
            label={__("Duplaicate", "forms-bridge")}
            showTooltip
            __next40pxDefaultSize
          >
            <CopyIcon
              width="25"
              height="25"
              color="var(--wp-components-color-accent,var(--wp-admin-theme-color,#3858e9))"
            />
          </Button>
          <Button
            variant="tertiary"
            style={{
              height: "40px",
              width: "40px",
              justifyContent: "center",
              fontSize: "1.5em",
              border: "1px solid",
              color: "gray",
            }}
            onClick={exportConfig}
            label={__("Download", "forms-bridge")}
            showTooltip
            __next40pxDefaultSize
          >
            <ArrowDownIcon width="12" height="20" color="gray" />
          </Button>
        </div>
      </div>
      <div
        style={
          isResponsive
            ? {
                paddingTop: "2rem",
                borderTop: "1px solid",
              }
            : {
                paddingLeft: "2rem",
                borderLeft: "1px solid",
                flex: 1,
              }
        }
      >
        <BackendHeaders
          headers={state.headers}
          setHeaders={(headers) => setState({ ...state, headers })}
        />
      </div>
    </div>
  );
}

function ArrowDownIcon({ width = 100, height = 145, color = "#000000" }) {
  return (
    <div style={{ transform: "translateY(-2px) rotate(180deg)" }}>
      <svg width={width} height={height} viewBox="0 0 100 145">
        <g transform="translate(-582.90398,-1448.5647)">
          <path
            style={{
              fill: color,
              strokeWidth: 1,
            }}
            d="m 669.08313,1526.3787 h -13.82086 v 33.5929 33.5931 h -22.37222 -22.37221 v -33.5931 -33.5929 h -13.83073 c -11.61145,0 -13.82303,-0.02 -13.78259,-0.1256 0.0391,-0.1018 19.69892,-30.7023 45.71154,-71.15 2.31274,-3.5961 4.23104,-6.5384 4.26292,-6.5384 0.0319,0 5.49256,8.4526 12.13488,18.7835 6.64231,10.3309 17.88488,27.8169 24.98352,38.8577 7.09864,11.0409 12.9066,20.0965 12.9066,20.1235 0,0.027 -6.21938,0.049 -13.82085,0.049 z"
          />
        </g>
      </svg>
    </div>
  );
}

function CopyIcon({ width = 26, height = 26, color = "#000000" }) {
  return (
    <svg width={width} height={height} viewBox="-1 0 26 26" fill="none">
      <path
        fillRule="evenodd"
        clipRule="evenodd"
        d="M17.676 14.248C17.676 15.8651 16.3651 17.176 14.748 17.176H7.428C5.81091 17.176 4.5 15.8651 4.5 14.248V6.928C4.5 5.31091 5.81091 4 7.428 4H14.748C16.3651 4 17.676 5.31091 17.676 6.928V14.248Z"
        stroke={color}
        strokeWidth="1.5"
        strokeLinecap="round"
        strokeLinejoin="round"
        fill="#ffffff00"
      />
      <path
        d="M10.252 20H17.572C19.1891 20 20.5 18.689 20.5 17.072V9.75195"
        stroke={color}
        strokeWidth="1"
        strokeLinecap="round"
        strokeLinejoin="round"
        fill="#ffffff00"
      />
    </svg>
  );
}

function BinIcon({ width = 40, height = 58.5 }) {
  return (
    <svg width={width} height={height} viewBox="0 0 40 58.5">
      <rect
        width="40"
        height="7.5808687"
        x="0"
        y="0"
        style={{
          fill: "#ffffff",
          strokeWidth: 4.99998,
          strokeLinecap: "round",
          strokeLinejoin: "round",
          strokeMiterlimit: 9,
        }}
      />
      <path
        d="M 0.43682956,9.0838308 3.2975745,53.79041 h 0.011404 c 0.2208728,2.623009 2.202482,4.721113 4.8482263,4.721113 H 31.69339 c 2.645747,0 4.627355,-2.098104 4.848227,-4.721113 h 0.0114 L 39.413767,9.0838308 H 34.077348 5.7732537 Z"
        style={{
          fill: "#ffffff",
          strokeWidth: 4.99998,
          strokeLinecap: "round",
          strokeLinejoin: "round",
          strokeMiterlimit: 9,
        }}
      />
    </svg>
  );
}
