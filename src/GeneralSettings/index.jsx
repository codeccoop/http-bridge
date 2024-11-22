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

export default function GeneralSettings() {
  const __ = wp.i18n.__;
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
