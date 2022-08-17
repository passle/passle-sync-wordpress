import { InputHTMLAttributes, useMemo } from "react";
import SettingsInput from "_Components/Molecules/SettingsInput/SettingsInput";

export type SettingsInputProps = InputHTMLAttributes<HTMLInputElement> & {
  label: string;
  description?: string;
};

const BoolSettingsInput = ({
  label,
  description,
  ...props
}: SettingsInputProps) => (
  <SettingsInput
    label={label}
    description={description}
    type="checkbox"
    {...props}
  />
);

export default BoolSettingsInput;
