import { InputHTMLAttributes, useMemo } from "react";
import SettingsInput from "_Components/Molecules/SettingsInput/SettingsInput";

export type SettingsInputProps = InputHTMLAttributes<HTMLInputElement> & {
  label: string;
  description?: string;
};

const TextSettingsInput = ({
  label,
  description,
  ...props
}: SettingsInputProps) => (
  <SettingsInput
    label={label}
    description={description}
    type="text"
    {...props}
  />
);

export default TextSettingsInput;
