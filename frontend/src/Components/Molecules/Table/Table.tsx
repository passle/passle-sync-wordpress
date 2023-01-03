import { ReactNode } from "react";
import TableNav from "_Components/Molecules/TableNav/TableNav";

export type TableProps = {
  currentPage?: number;
  itemsPerPage: number;
  totalItems: number;
  totalPages?: number;
  ActionsLeft?: ReactNode;
  ActionsRight?: ReactNode;
  Head: ReactNode;
  Body: ReactNode;
  setCurrentPage?: (page: number) => Promise<void>;
};

const Table = ({
  currentPage = 1,
  itemsPerPage,
  totalItems,
  totalPages = 1,
  ActionsLeft = null,
  ActionsRight = null,
  setCurrentPage = async () => {},
  Head,
  Body,
}: TableProps) => {
  return (
    <div>
      <TableNav
        currentPage={currentPage}
        itemsPerPage={itemsPerPage}
        totalItems={totalItems}
        totalPages={totalPages}
        ActionsLeft={ActionsLeft}
        ActionsRight={ActionsRight}
        setCurrentPage={setCurrentPage}
      />

      <table className="wp-list-table widefat fixed striped table-view-list">
        {/* Table header */}
        <thead>
          <tr>{Head}</tr>
        </thead>

        {/* Table body */}
        <tbody>{Body}</tbody>

        {/* Table footer */}
        <tfoot>
          <tr>{Head}</tr>
        </tfoot>
      </table>
    </div>
  );
};

export default Table;
