import { ReactNode, useEffect } from "react";
import Portal from "_Components/Atoms/Portal/Portal";
import classNames from "_Utils/classNames";
import styles from "./Modal.module.scss";

export type ModalProps = {
  open: boolean;
  title: string;
  text?: string;
  buttons?: ReactNode;
  onCancel?: () => void;
};

const Modal = (props: ModalProps) => {
  useEffect(() => {
    const closeOnEscapeKey = (e: KeyboardEvent) =>
      e.key === "Escape" ? props.onCancel() : null;

    document.body.addEventListener("keydown", closeOnEscapeKey);

    return () => {
      document.body.removeEventListener("keydown", closeOnEscapeKey);
    };
  }, [props.onCancel]);

  useEffect(() => {
    if (props.open) {
      document.body.style.setProperty("overflow", "hidden");
      document.body.style.setProperty("padding-right", "0");
    } else {
      document.body.style.removeProperty("overflow");
      document.body.style.removeProperty("padding-right");
    }

    return () => {
      document.body.style.removeProperty("overflow");
      document.body.style.removeProperty("padding-right");
    };
  }, [props.open]);

  const onCancel = () => {
    if (props.onCancel) props.onCancel();
  };

  if (!props.open) return null;

  return (
    <Portal wrapperId="passle-sync-modal-wrapper">
      <div className={styles.ModalBG} onClick={onCancel}>
        <div className={styles.Modal} onClick={(e) => e.stopPropagation()}>
          <h2 className={styles.Modal_Title}>{props.title}</h2>
          <p className={styles.Modal_Content}>{props.text}</p>
          {props.buttons && (
            <div className={styles.Modal_Buttons}>{props.buttons}</div>
          )}
        </div>
      </div>
    </Portal>
  );
};

export default Modal;
