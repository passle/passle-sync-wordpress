import classNames from "_Utils/classNames";
import styles from "./Tab.module.scss";

export type TabProps = {
  text: string;
  active: boolean;
  disabled: boolean;
  onClick: () => void;
};

const Tab = (props: TabProps) => {
  const onClick = () => {
    if (props.disabled) return;
    props.onClick();
  };

  return (
    <button
      className={classNames(
        styles.Tab,
        props.active && styles.Tab___Active,
        props.disabled && styles.Tab___Disabled,
      )}
      onClick={onClick}>
      {props.text}
    </button>
  );
};

export default Tab;
