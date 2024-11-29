// vendor
import React from "react";
import domReady from "@wordpress/dom-ready";
import { createRoot } from "@wordpress/element";

// source
import SettingsPage from "./SettingsPage/index.jsx";
import ErrorBoundary from "./ErrorBoundary.jsx";

domReady(() => {
  const root = createRoot(document.getElementById("http-bridge"));

  root.render(
    <div style={{ position: "relative" }}>
      <ErrorBoundary fallback={<h1>Error</h1>}>
        <SettingsPage />
      </ErrorBoundary>
    </div>
  );
});
