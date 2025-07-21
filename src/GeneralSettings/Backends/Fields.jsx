import ContentType from "./ContentType";
import BackendAuthentication from "./Authentication";

const { TextControl } = wp.components;
const { __ } = wp.i18n;

export default function BackendFields({ state, setState, errors }) {
  return (
    <>
      <div style={{ maxWidth: "100%", width: `clamp(200px, 15vw, 300px)` }}>
        <TextControl
          label={__("Name", "forms-bridge")}
          help={
            errors.name && __("This name is already in use", "forms-bridge")
          }
          value={state.name}
          onChange={(name) => setState({ ...state, name })}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
      </div>
      <div style={{ maxWidth: "100%", width: `clamp(200px, 15vw, 300px)` }}>
        <TextControl
          label={__("Base URL", "forms-bridge")}
          help={errors.base_url && __("Invalid base URL", "forms-bridge")}
          value={state.base_url}
          onChange={(base_url) => setState({ ...state, base_url })}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
      </div>
      <ContentType
        headers={state.headers}
        setHeaders={(headers) => setState({ ...state, headers })}
      />
      <BackendAuthentication
        data={state.authentication}
        setData={(authentication) => setState({ ...state, authentication })}
      />
    </>
  );
}
