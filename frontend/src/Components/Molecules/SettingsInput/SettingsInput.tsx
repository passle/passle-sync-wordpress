import { InputHTMLAttributes, useMemo } from "react";

export type SettingsInputProps = InputHTMLAttributes<HTMLInputElement> & {
  label: string;
  description?: string;
};

const SettingsInput = ({
  label,
  description,
  type,
  ...props
}: SettingsInputProps) => {
  const name = useMemo(
    () => label.trim().replace(/ +/g, "-").toLowerCase(),
    [label],
  );

  return (
    <tr>
      <th className="row">
        <label htmlFor={name}>{label}</label>
      </th>
      <td>
        <input type={type} id={name} className="regular-text code" {...props} />
        {description && (
          <p className="description" id={`${name}-description`}>
            {description}
          </p>
        )}
      </td>
    </tr>
  );
};

export default SettingsInput;
