// source
import { useGeneral } from "../providers/Settings";
import Backends from "./Backends";

const {
  Card,
  CardHeader,
  CardBody,
  __experimentalHeading: Heading,
  PanelRow,
  ToggleControl,
  __experimentalSpacer: Spacer,
} = wp.components;
const { __ } = wp.i18n;

export default function GeneralSettings() {
  const [{ whitelist, backends }, save] = useGeneral();

  const update = (field) => save({ whitelist, backends, ...field });

  return (
    <Card size="large" style={{ height: "fit-content" }}>
      <CardHeader>
        <Heading level={3}>{__("General", "http-bridge")}</Heading>
      </CardHeader>
      <CardBody>
        <PanelRow>
          <ToggleControl
            label={__("Block connections from unkown origins", "http-bridge")}
            checked={whitelist}
            onChange={() => update({ whitelist: !whitelist })}
            __nextHasNoMarginBottom
            help={__(
              "Should HTTP Bridge block requests from origins not listed as backends? If active, incomming connections should include HTTP Origin header",
              "http-bridge"
            )}
          />
        </PanelRow>
        <Spacer paddingY="calc(8px)" />
        <PanelRow>
          <Backends
            backends={backends}
            setBackends={(backends) => update({ backends })}
          />
        </PanelRow>
      </CardBody>
    </Card>
  );
}
