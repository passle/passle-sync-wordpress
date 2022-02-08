import { useState } from "react";
import classNames from "_Utils/classNames";
import Spinner from "_Components/Atoms/Spinner/Spinner";
import "./LoadingButton.scss";

export type LoadingButtonProps = {
  text: string;
  loadingText: string;
  className?: string;
  callback: (fn: () => void) => void;
};

const LoadingButton = (props: LoadingButtonProps) => {
  const [loading, setLoading] = useState(false);

  const handleClick = () => {
    if (loading) return;

    setLoading(true);
    props.callback(finishLoading);
  };

  const finishLoading = () => {
    setLoading(false);
  };

  return (
    <button
      disabled={loading}
      className={classNames("button button-primary", props.className)}
      onClick={handleClick}
    >
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

export default LoadingButton;
