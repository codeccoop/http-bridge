// vendor
import React from "react";
import apiFetch from "@wordpress/api-fetch";
import {
  createContext,
  useContext,
  useState,
  useEffect,
  useRef,
} from "@wordpress/element";

const defaultSettings = {
  general: {
    whitelist: false,
    backends: [],
  },
};

const SettingsContext = createContext([defaultSettings, () => {}]);

export default function SettingsProvider({ children, setLoading }) {
  const persisted = useRef(true);

  const [general, setGeneral] = useState({ ...defaultSettings.general });

  const fetchSettings = () => {
    setLoading(true);
    return apiFetch({
      path: `${window.wpApiSettings.root}wp-bridges/v1/http-bridge/settings`,
      headers: {
        "X-WP-Nonce": wpApiSettings.nonce,
      },
    })
      .then((settings) => {
        setGeneral(settings.general);
      })
      .finally(() => {
        setLoading(false);
        setTimeout(() => {
          persisted.current = true;
        }, 500);
      });
  };

  const beforeUnload = useRef((ev) => {
    if (!persisted.current) {
      ev.preventDefault();
      ev.returnValue = true;
    }
  }).current;

  useEffect(() => {
    fetchSettings();
    window.addEventListener("beforeunload", (ev) => beforeUnload(ev));
  }, []);

  useEffect(() => {
    persisted.current = false;
  }, [general]);

  const saveSettings = () => {
    setLoading(true);
    return apiFetch({
      path: `${window.wpApiSettings.root}wp-bridges/v1/http-bridge/settings`,
      method: "POST",
      headers: {
        "X-WP-Nonce": wpApiSettings.nonce,
      },
      mode: "same-origin",
      data: { general },
    }).then(fetchSettings);
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
      {children}
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
