// vendor
import React from "react";
import {
  Card,
  CardHeader,
  CardBody,
  __experimentalHeading as Heading,
  PanelRow,
  ToggleControl,
  __experimentalSpacer as Spacer,
} from "@wordpress/components";

// source
import { useGeneral } from "../providers/Settings";
import Backends from "./Backends";
import { useI18n } from "../providers/I18n";

export default function GeneralSettings() {
  const __ = useI18n();
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
            label={__("Block connections from unkown origins", "http-brige")}
            checked={whitelist}
            onChange={() => update({ whitelist: !whitelist })}
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
