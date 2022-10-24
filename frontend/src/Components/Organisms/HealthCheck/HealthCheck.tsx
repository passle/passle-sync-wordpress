import Penpal from "penpal";
import { useEffect, useRef } from "react";
import useOptions from "_Hooks/useOptions";
import styles from "./HealthCheck.module.scss";

const HealthCheck = () => {
  const iframeContainer = useRef<HTMLDivElement>(null);

  const { options } = useOptions();

  useEffect(() => {
    Penpal.connectToChild({
      url: `https://www.passle.${options.domainExt}/cms-integration-health-check`,
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
