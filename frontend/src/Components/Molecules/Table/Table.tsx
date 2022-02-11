import { ReactNode } from "react";
import TableNav from "_Components/Molecules/TableNav/TableNav";

export type TableProps = {
  currentPage: number;
  itemsPerPage: number;
  totalItems: number;
  totalPages: number;
  ActionsLeft: ReactNode;
  ActionsRight: ReactNode;
  Head: ReactNode;
  Body: ReactNode;
};

const Table = (props: TableProps) => {
  return (
    <div>
      <TableNav
        currentPage={props.currentPage}
        itemsPerPage={props.itemsPerPage}
        totalItems={props.totalItems}
        totalPages={props.totalPages}
        ActionsLeft={props.ActionsLeft}
        ActionsRight={props.ActionsRight}
      />

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
    </div>
  );
};

export default Table;
