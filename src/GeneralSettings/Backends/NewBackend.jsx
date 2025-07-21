import useBackendNames from "../../hooks/useBackendNames";
import { uploadJson, validateBackend, validateUrl } from "../../lib/utils";
import BackendFields from "./Fields";
import BackendHeaders from "./Headers";
import useResponsive from "../../hooks/useResponsive";

const { Button } = wp.components;
const { useState, useMemo, useCallback } = wp.element;
const { __ } = wp.i18n;

const TEMPLATE = {
  name: "",
  base_url: "https://",
  headers: [
    {
      name: "Content-Type",
      value: "application/json",
    },
  ],
  authentication: {},
};

export default function NewBackend({ add }) {
  const isResponsive = useResponsive();

  const [data, setData] = useState(TEMPLATE);

  const names = useBackendNames();

  const nameConflict = useMemo(() => {
    if (!data.name) return false;
    return names.has(data.name.trim());
  }, [names, data.name]);

  const invalidUrl = useMemo(() => {
    return !validateUrl(data.base_url, true);
  }, [data.base_url]);

  const create = () => {
    setData(TEMPLATE);
    add({ ...data });
  };

  const isValid = useMemo(() => {
    return !nameConflict && !invalidUrl && validateBackend(data);
  }, [data, nameConflict, invalidUrl]);

  const uploadConfig = useCallback(() => {
    uploadJson()
      .then((data) => {
        const isValid = validateBackend(data);

        if (!isValid) {
          return;
        }

        let i = 1;
        while (names.has(data.name)) {
          data.name = data.name.replace(/\([0-9]+\)/, "") + ` (${i})`;
          i++;
        }

        data.headers =
          (Array.isArray(data.headers) &&
            data.headers.filter(
              (header) => header && header.name && header.value
            )) ||
          [];

        add(data);
      })
      .catch((err) => {
        if (!err) return;

        console.error(err);
      });
  }, [names]);

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
          state={data}
          setState={setData}
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
            onClick={create}
            style={{ width: "100px", justifyContent: "center" }}
            disabled={nameConflict || !isValid}
            __next40pxDefaultSize
          >
            {__("Add", "forms-bridge")}
          </Button>
          <Button
            variant="tertiary"
            size="compact"
            style={{
              width: "40px",
              height: "40px",
              justifyContent: "center",
              fontSize: "1.5em",
              border: "1px solid",
              color: "gray",
            }}
            onClick={uploadConfig}
            __next40pxDefaultSize
            label={__("Upload", "forms-bridge")}
            showTooltip
          >
            <ArrowUpIcon width="12" height="20" color="gray" />
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
          headers={data.headers}
          setHeaders={(headers) => setData({ ...data, headers })}
        />
      </div>
    </div>
  );
}

function ArrowUpIcon({ width = 100, height = 145, color = "#000000" }) {
  return (
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
  );
}
