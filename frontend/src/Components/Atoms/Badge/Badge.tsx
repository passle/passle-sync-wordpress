import classNames from "_Utils/classNames";
import styles from "./Badge.module.scss";

export type BadgeProps = {
  variant: "success" | "warning";
  text: string;
};

const Badge = (props: BadgeProps) => {
  return (
    <div
      className={classNames(
        styles.Badge,
        props.variant === "success" && styles.Badge___Success,
        props.variant === "warning" && styles.Badge___Warning,
      )}>
      {props.text}
    </div>
  );
};

export default Badge;
