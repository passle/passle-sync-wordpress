import { useState } from "react";
import classNames from "_Utils/classNames";
import Spinner from "_Components/Atoms/Spinner/Spinner";
import styles from "./Button.module.scss";

type ButtonVariant = "primary" | "secondary";

type RegularButtonProps = {
  loadingText?: never;
  onClick: () => void;
};

type LoadingButtonProps = {
  loadingText: string;
  onClick: (cb: () => void) => void;
};

export type ButtonProps = (RegularButtonProps | LoadingButtonProps) & {
  text: string;
  variant?: ButtonVariant;
  className?: string;
  disabled?: boolean;
};

const Button = ({
  variant = "primary",
  disabled = false,
  ...props
}: ButtonProps) => {
  const [loading, setLoading] = useState(false);

  const onClick = () => {
    if (loading || disabled) return;

    if ("loadingText" in props) {
      setLoading(true);
      props.onClick(finishLoading);
    } else {
      props.onClick();
    }
  };

  const finishLoading = () => {
    setLoading(false);
  };

  return (
    <button
      disabled={loading || disabled}
      className={classNames(
        "button",
        variant === "primary" && "button-primary",
        variant === "secondary" && "button-secondary",
        styles.Button,
        props.className,
      )}
      onClick={onClick}>
      {loading ? (
        <>
          <Spinner />
          {props.loadingText}
        </>
      ) : (
        <>{props.text}</>
      )}
    </button>
  );
};

export default Button;
