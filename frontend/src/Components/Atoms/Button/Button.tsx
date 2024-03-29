import { ReactNode, useState } from "react";
import classNames from "_Utils/classNames";
import Spinner from "_Components/Atoms/Spinner/Spinner";
import styles from "./Button.module.scss";
import useIsMounted from "_Hooks/useIsMounted";

type ButtonVariant = "primary" | "secondary";

type RegularButtonProps = {
  loadingContent?: never;
  onClick: () => void;
};

type LoadingButtonProps = {
  loadingContent: ReactNode;
  onClick: (cb: () => void) => void;
};

export type ButtonProps = (RegularButtonProps | LoadingButtonProps) & {
  content: ReactNode;
  variant?: ButtonVariant;
  className?: string;
  disabled?: boolean;
  hideSpinner?: boolean;
};

const Button = ({
  variant = "primary",
  disabled = false,
  hideSpinner = false,
  ...props
}: ButtonProps) => {
  const isMounted = useIsMounted();
  const [loading, setLoading] = useState(false);

  const onClick = () => {
    if (loading || disabled) return;

    if ("loadingContent" in props) {
      setLoading(true);
      props.onClick(finishLoading);
    } else {
      props.onClick();
    }
  };

  const finishLoading = () => {
    if (!isMounted.current) return;
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
          {!hideSpinner && <Spinner />}
          {props.loadingContent}
        </>
      ) : (
        <>{props.content}</>
      )}
    </button>
  );
};

export default Button;
