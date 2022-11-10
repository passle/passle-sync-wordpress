import { ReactNode, useState } from "react";
import Tab from "_Components/Atoms/Tab/Tab";
import styles from "./Tabs.module.scss";

type TabType = {
  label: string;
  disabled?: boolean;
  Content: ReactNode;
};

export type TabsProps = {
  tabs: TabType[];
  loading: boolean;
};

const Tabs = (props: TabsProps) => {
  const [activeIdx, setActiveIdx] = useState(0);

  return (
    <>
      <div className={styles.TabList}>
        {props.tabs.map((tab, idx) => (
          <Tab
            key={tab.label}
            text={tab.label}
            active={activeIdx === idx}
            disabled={
              (props.loading && activeIdx !== idx) || (tab.disabled ?? false)
            }
            onClick={() => setActiveIdx(idx)}
          />
        ))}
      </div>
      <div className={styles.TabContent}>{props.tabs[activeIdx].Content}</div>
    </>
  );
};

export default Tabs;
