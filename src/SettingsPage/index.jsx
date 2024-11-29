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
import SettingsProvider, { useSubmitSettings } from "../providers/Settings";
import GeneralSettings from "../GeneralSettings";
import Spinner from "../Spinner";

const tabs = [
  {
    name: "general",
    title: "General",
  },
];

function SaveButton({ loading, setLoading }) {
  const __ = wp.i18n.__;
  const submit = useSubmitSettings();

  const [error, setError] = useState(false);

  const onClick = () => {
    setLoading(true);
    submit()
      .then(() => setLoading(false))
      .catch(() => {
        console.error(err);
        setError(true);
      });
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
  const __ = wp.i18n.__;

  const [loaders, setLoaders] = useState([]);

  const loading = loaders.length > 0;
  const setLoading = (state) => {
    const newLoaders = loaders
      .slice(1)
      .concat(state)
      .filter((state) => state);
    setLoaders(newLoaders);
  };

  return (
    <SettingsProvider setLoading={setLoading}>
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
      <SaveButton loading={loading} setLoading={setLoading} />
      <Spinner show={loading} />
    </SettingsProvider>
  );
}
