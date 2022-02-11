import { useMemo } from "react";

export type TableHeadingProps = {
  label: string;
};

const TableHeading = (props: TableHeadingProps) => {
  const name = useMemo(
    () => props.label.toLowerCase().replace(/\s+/g, "-"),
    [props.label],
  );

  return (
    <th scope="col" id={name} className="manage-column">
      <span>{props.label}</span>
    </th>
  );
};

export default TableHeading;
