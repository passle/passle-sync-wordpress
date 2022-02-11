import classNames from "_Utils/classNames";
import styles from "./Tab.module.scss";

export type TabProps = {
  text: string;
  active: boolean;
  onClick: () => void;
};

const Tab = (props: TabProps) => {
  return (
    <button
      className={classNames(styles.Tab, props.active && styles.Tab___Active)}
      onClick={props.onClick}>
      {props.text}
    </button>
  );
};

export default Tab;
