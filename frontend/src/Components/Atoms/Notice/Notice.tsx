import { ReactNode } from "react";
import classNames from "_Utils/classNames";

export type NoticeProps = {
  type: "success" | "error" | "info";
  content: ReactNode;
  onDismiss?: () => void;
};

const Notice = (props: NoticeProps) => {
  return (
    <div
      id="message"
      className={classNames(
        `notice notice-${props.type}`,
        props.onDismiss && "is-dismissible",
      )}>
      <p>{props.content}</p>
      {props.onDismiss && (
        <button
          type="button"
          className="notice-dismiss"
          onClick={() => props.onDismiss()}>
          <span className="screen-reader-text">Dismiss this notice.</span>
        </button>
      )}
    </div>
  );
};

export default Notice;
