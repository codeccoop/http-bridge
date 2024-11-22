// vendor
import React from "react";
import {
  TabPanel,
  __experimentalHeading as Heading,
  Button,
  __experimentalSpacer as Spacer,
} from "@wordpress/components";
import { useState } from "@wordpress/element";

// source
import I18nProvider, { useI18n } from "../providers/I18n";
import SettingsProvider, { useSubmitSettings } from "../providers/Settings";
import GeneralSettings from "../GeneralSettings";

const tabs = [
  {
    name: "general",
    title: "General",
  },
];

function SaveButton() {
  const __ = useI18n();
  const submit = useSubmitSettings();

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(false);

  const onClick = () => {
    setLoading(true);
    submit()
      .then(() => setLoading(false))
      .catch(() => setError(true));
  };

  return (
    <Button
      variant={error ? "secondary" : "primary"}
      onClick={onClick}
      style={{ minWidth: "130px", justifyContent: "center" }}
      disabled={loading}
      __next40pxDefaultSize
    >
      {(error && __("Error", "http-bridge")) || __("Save", "http-bridge")}
    </Button>
  );
}

export default function SettingsPage() {
  const __ = useI18n();
  return (
    <I18nProvider>
      <SettingsProvider>
        <Heading level={1}>HTTP Bridge</Heading>
        <TabPanel
          initialTabName="general"
          tabs={tabs.map(({ name, title }) => ({
            name,
            title: __(title, "http-bridge"),
          }))}
        >
          {() => (
            <>
              <Spacer />
              <GeneralSettings />
            </>
          )}
        </TabPanel>
        <Spacer />
        <SaveButton />
      </SettingsProvider>
    </I18nProvider>
  );
}
