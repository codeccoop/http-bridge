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

import useDiff from "../hooks/useDiff";

const defaultSettings = {
  general: {
    whitelist: false,
    backends: [],
  },
};

const SettingsContext = createContext([defaultSettings, () => {}]);

export default function SettingsProvider({ children, setLoading }) {
  const initialState = useRef(null);
  const currentState = useRef(defaultSettings.general);
  const [general, setGeneral] = useState({ ...defaultSettings.general });
  currentState.current = general;

  const fetchSettings = () => {
    setLoading(true);
    return apiFetch({
      path: `${window.wpApiSettings.root}http-bridge/v1/settings`,
      headers: {
        "X-WP-Nonce": wpApiSettings.nonce,
      },
    })
      .then((settings) => {
        setGeneral(settings.general);
        initialState.current = settings.general;
      })
      .finally(() => {
        setLoading(false);
      });
  };

  const beforeUnload = useRef((ev) => {
    const state = currentState.current;
    if (useDiff(state, initialState.current)) {
      ev.preventDefault();
      ev.returnValue = true;
    }
  }).current;

  useEffect(() => {
    fetchSettings();
    window.addEventListener("beforeunload", (ev) => beforeUnload(ev));
  }, []);

  const saveSettings = () => {
    setLoading(true);
    return apiFetch({
      path: `${window.wpApiSettings.root}http-bridge/v1/settings`,
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
