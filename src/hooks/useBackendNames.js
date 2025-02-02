// source
import { useGeneral } from "../providers/Settings";

const { useMemo } = wp.element;

export default function useBackendNames() {
  const [{ backends }] = useGeneral();

  return useMemo(() => {
    return new Set(backends.map(({ name }) => name));
  }, [backends]);
}
