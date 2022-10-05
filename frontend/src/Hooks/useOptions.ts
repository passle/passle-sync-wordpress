import { useContext } from "react";
import { OptionsContext } from "_Contexts/OptionsContext";

const useOptions = () => {
  const { options } = useContext(OptionsContext);
  return options;
};

export default useOptions;
