import { ReactNode } from "react";

export type TableProps = {
  Head: ReactNode;
  Body: ReactNode;
};

const Table = (props: TableProps) => {
  return (
    <table className="wp-list-table widefat fixed striped table-view-list">
      {/* Table header */}
      <thead>
        <tr>{props.Head}</tr>
      </thead>

      {/* Table body */}
      <tbody>{props.Body}</tbody>

      {/* Table footer */}
      <tfoot>
        <tr>{props.Head}</tr>
      </tfoot>
    </table>
  );
};

export default Table;
