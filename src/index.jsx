// source
import SettingsPage from "./SettingsPage/index.jsx";
import ErrorBoundary from "./ErrorBoundary.jsx";

const domReady = wp.domReady;
const { createRoot } = wp.element;

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
