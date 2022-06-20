import { ReactNode } from "react";
import classNames from "_Utils/classNames";

export type NoticeProps = {
  success: boolean;
  content: ReactNode;
  onDismiss: () => void;
};

const Notice = (props: NoticeProps) => {
  return (
    <div
      id="message"
      className={classNames(
        "notice is-dismissible",
        props.success ? "notice-success" : "notice-error",
      )}>
      <p>{props.content}</p>
      <button
        type="button"
        className="notice-dismiss"
        onClick={() => props.onDismiss()}>
        <span className="screen-reader-text">Dismiss this notice.</span>
      </button>
    </div>
  );
};

export default Notice;
