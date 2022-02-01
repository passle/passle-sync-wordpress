import { useState } from "react";

function LoadingButton({ text, loadingText, callback, className }) {
  const [loading, setLoading] = useState(false);

  const handleClick = () => {
    setLoading(true);
    callback(finishLoading);
  };

  const finishLoading = () => {
    setLoading(false);
  };

  return (
    <>
      {!loading && <button className={"button button-primary " + (className ?? "")} onClick={() => handleClick()}>{text}</button>}
      {loading && <p>{loadingText}</p>}
    </>
  );
}

export default LoadingButton;
