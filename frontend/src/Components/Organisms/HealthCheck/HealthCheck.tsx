import Penpal from "penpal";
import { useEffect, useMemo, useRef } from "react";
import { Options } from "_API/Types/Options";
import styles from "./HealthCheck.module.scss";

const HealthCheck = () => {
  const iframeContainer = useRef<HTMLDivElement>(null);

  const options = useMemo<Options>(
    () =>
      JSON.parse(
        document.getElementById("passle-sync-settings-root").dataset
          .passlesyncOptions,
      ),
    [],
  );

  const domainExt = useMemo<string>(
    () =>
      document.getElementById("passle-sync-settings-root").dataset
        .passlesyncDomainExt,
    [],
  );

  useEffect(() => {
    Penpal.connectToChild({
      url: `https://www.passle.${domainExt}/cms-integration-health-check`,
      appendTo: iframeContainer.current,
      methods: {
        getOptions() {
          return {
            apiKey: options.passleApiKey,
            passleShortcodes: options.passleShortcodes,
          };
        },
      },
    });
  }, []);

  return <div ref={iframeContainer} className={styles.IframeContainer}></div>;
};

export default HealthCheck;
