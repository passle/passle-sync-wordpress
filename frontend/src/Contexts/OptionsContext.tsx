import { createContext, ReactNode, useMemo } from "react";
import { Options } from "_API/Types/Options";

export type OptionsContextType = {
  options: Options;
};

export const OptionsContext = createContext<OptionsContextType>({
  options: null,
});

export type OptionsContextProviderProps = {
  children?: ReactNode;
};

export const OptionsContextProvider = (props: OptionsContextProviderProps) => {
  const options = useMemo<Options>(
    () =>
      JSON.parse(
        document.getElementById("passle-sync-settings-root").dataset
          .passlesyncOptions,
      ),
    [],
  );

  return (
    <OptionsContext.Provider value={{ options }}>
      {props.children}
    </OptionsContext.Provider>
  );
};
