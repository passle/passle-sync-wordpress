import { useState } from "react";

export type LoadingButtonProps = {
  text: string;
  loadingText: string;
  callback: (fn: () => void) => void;
  className?: string;
};

const LoadingButton = (props: LoadingButtonProps) => {
  const [loading, setLoading] = useState(false);

  const handleClick = () => {
    setLoading(true);
    props.callback(finishLoading);
  };

  const finishLoading = () => {
    setLoading(false);
  };

  return (
    <>
      {!loading && (
        <button
          className={"button button-primary " + (props.className ?? "")}
          onClick={handleClick}
        >
          {props.text}
        </button>
      )}
      {loading && <p>{props.loadingText}</p>}
    </>
  );
};

export default LoadingButton;
