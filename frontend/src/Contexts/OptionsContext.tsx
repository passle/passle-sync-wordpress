import { createContext, ReactNode, useMemo, useState } from "react";
import { Options } from "_API/Types/Options";

export type OptionsContextType = {
  options: Options;
  setOptions: (options: Options) => void;
};

export const OptionsContext = createContext<OptionsContextType>({
  options: null,
  setOptions: () => {},
});

export type OptionsContextProviderProps = {
  children?: ReactNode;
};

export const OptionsContextProvider = (props: OptionsContextProviderProps) => {
  let [options, setOptions] = useState<Options>(() =>
    JSON.parse(
      document.getElementById("passle-sync-settings-root").dataset
        .passlesyncOptions,
    ),
  );

  return (
    <OptionsContext.Provider value={{ options, setOptions }}>
      {props.children}
    </OptionsContext.Provider>
  );
};
