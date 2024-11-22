// vendor
import React from "react";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {
  createContext,
  useContext,
  useState,
  useEffect,
} from "@wordpress/element";

// source
import Loading from "../Loading";

const noop = () => {};

const defaultSettings = {
  general: {
    whitelist: false,
    backends: [],
  },
};

const SettingsContext = createContext([defaultSettings, noop]);

export default function SettingsProvider({ children }) {
  const [general, setGeneral] = useState({ ...defaultSettings.general });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    apiFetch({
      path: `${window.wpApiSettings.root}wp-bridges/v1/http/settings`,
      headers: {
        "X-WP-Nonce": wpApiSettings.nonce,
      },
    })
      .then((settings) => {
        setGeneral(settings.general);
      })
      .finally(() => setLoading(false));
  }, []);

  const saveSettings = () => {
    return apiFetch({
      path: `${window.wpApiSettings.root}wp-bridges/v1/http/settings`,
      method: "POST",
      headers: {
        "X-WP-Nonce": wpApiSettings.nonce,
      },
      mode: "same-origin",
      data: { general },
    });
  };

  return (
    <SettingsContext.Provider
      value={[
        {
          general,
          setGeneral,
        },
        saveSettings,
      ]}
    >
      {(loading && <Loading message={__("Loading", "http-bridge")} />) ||
        children}
    </SettingsContext.Provider>
  );
}

export function useGeneral() {
  const [{ general, setGeneral }] = useContext(SettingsContext);

  const { whitelist, backends } = general;

  const update = ({ whitelist, backends }) =>
    setGeneral({
      whitelist,
      backends,
    });

  return [{ whitelist, backends }, update];
}

export function useSubmitSettings() {
  const [, submit] = useContext(SettingsContext);
  return submit;
}
