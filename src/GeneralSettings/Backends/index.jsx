// source
import useBackendNames from "../../hooks/useBackendNames";
import Backend from "./Backend";
import NewBackend from "./NewBackend";

const { useRef, useEffect } = wp.element;
const { TabPanel, __experimentalSpacer: Spacer } = wp.components;
const { __ } = wp.i18n;

const CSS = `.backends-tabs-panel .components-tab-panel__tabs{overflow-x:auto;}
.backends-tabs-panel .components-tab-panel__tabs>button{flex-shrink:0;}`;

export default function Backends({ backends, setBackends }) {
  const names = useBackendNames();

  const tabs = backends
    .map(({ name }, index) => ({
      index,
      name: String(index),
      title: name,
      icon: <TabTitle name={name} />,
    }))
    .concat([
      {
        index: -1,
        name: "new",
        title: __("Add a backend", "forms-bridge"),
        icon: (
          <div style={{ marginBottom: "-2px" }}>
            <AddIcon width="15" height="15" />
          </div>
        ),
      },
    ]);

  const updateBackend = (index, data) => {
    if (index === -1) index = backends.length;

    if (!data.headers?.length) {
      data.headers = [{ name: "Content-Type", value: "application/json" }];
    }

    data.name = data.name.trim();
    data.base_url = data.base_url.trim();

    const newBackends = backends
      .slice(0, index)
      .concat([data])
      .concat(backends.slice(index + 1, backends.length));

    setBackends(newBackends);
  };

  const removeBackend = ({ name }) => {
    const index = backends.findIndex((b) => b.name === name);

    const newBackends = backends
      .slice(0, index)
      .concat(backends.slice(index + 1));

    setBackends(newBackends);
  };

  const copyBackend = (name) => {
    const i = backends.findIndex((backend) => backend.name === name);
    const backend = backends[i];
    const copy = { ...backend };

    copy.name = copy.name.trim();
    copy.base_url = copy.base_url.trim();

    while (names.has(copy.name)) {
      copy.name += "-copy";
    }

    setBackends(backends.concat(copy));
  };

  const style = useRef(document.createElement("style"));
  useEffect(() => {
    style.current.appendChild(document.createTextNode(CSS));
    document.head.appendChild(style.current);

    return () => {
      document.head.removeChild(style.current);
    };
  }, []);

  return (
    <div style={{ width: "100%" }}>
      <p>
        {__(
          "Configure your backend connexions and reuse them on your bridges",
          "forms-bridge"
        )}
      </p>
      <Spacer paddingBottom="5px" />
      <TabPanel tabs={tabs} className="backends-tabs-panel">
        {(tab) => {
          const backend = backends[tab.index];

          if (!backend) {
            return (
              <NewBackend add={(data) => updateBackend(tab.index, data)} />
            );
          }

          return (
            <Backend
              data={backend}
              remove={removeBackend}
              update={(newBackend) => updateBackend(tab.index, newBackend)}
              copy={() => copyBackend(backend.name)}
            />
          );
        }}
      </TabPanel>
    </div>
  );
}

function TabTitle({ name }) {
  return (
    <div style={{ position: "relative", padding: "0px 5px" }}>
      <span>{name}</span>
    </div>
  );
}

function AddIcon({ width = 30, height = 30 }) {
  return (
    <svg width={width} height={height} viewBox="0 0 30 30">
      <path
        d="M 2.622057,29.862373 C 1.828663,29.690276 1.123542,29.19942 0.655787,28.493598 -0.033852,27.452962 -0.001626,28.119212 9.16e-4,14.954001 0.003016,4.075681 0.012016,3.372831 0.157468,2.730124 0.469143,1.352914 1.392042,0.445513 2.777525,0.154064 3.411524,0.020696 3.979927,0.002724 7.604454,0.001448 L 11.712762,4.8e-5 V 1.685006 3.369963 H 7.54125 3.369737 v 11.626209 11.626209 h 11.587535 c 6.373144,0 11.607885,-2e-6 11.632757,-4e-6 0.02487,-1e-6 0.04316,-1.857667 0.04063,-4.128146 l -0.0046,-4.128143 h 1.686967 1.686968 v 4.206886 c 0,4.848747 -0.02041,5.049055 -0.604115,5.927982 -0.468374,0.705269 -1.186385,1.192115 -2.015216,1.366413 -0.855959,0.180003 -23.927215,0.175391 -24.758611,-0.0049 z M 19.971514,13.353291 V 10.0255 H 16.64095 13.310386 l 0.02285,-1.663896 0.02285,-1.663896 3.307715,-0.02204 3.307714,-0.02204 V 3.326814 0 h 1.68546 1.685459 V 3.327791 6.655583 H 26.671217 30 v 1.684958 1.684958 h -3.328783 -3.328783 v 3.327791 3.327792 h -1.68546 -1.68546 z"
        style={{ strokeWidth: 1 }}
      />
    </svg>
  );
}
