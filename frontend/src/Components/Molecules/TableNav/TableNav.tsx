import { ReactNode, useEffect, useState } from "react";
import Button from "_Components/Atoms/Button/Button";
import classNames from "_Utils/classNames";
import styles from "./TableNav.module.scss";

export type TableNavProps = {
  currentPage: number;
  itemsPerPage: number;
  totalItems: number;
  totalPages: number;
  ActionsLeft: ReactNode;
  ActionsRight: ReactNode;
  setCurrentPage: (page: number) => Promise<void>;
};

const TableNav = (props: TableNavProps) => {
  const [currentPageInput, setCurrentPageInput] = useState("");

  useEffect(() => {
    setCurrentPageInput(props.currentPage?.toString());
  }, [props.currentPage]);

  return (
    <div className={styles.TableNav}>
      <div className={classNames("actions", styles.TableNav_Actions)}>
        <div className={styles.TableNav_ActionGroup}>{props.ActionsLeft}</div>
        <div
          className={styles.TableNav_ActionGroup}
          style={{ marginRight: 20 }}>
          {props.ActionsRight}
        </div>
      </div>
      <div className={classNames("tablenav-pages", styles.TableNav_Pages)}>
        <span className="displaying-num" style={{ marginRight: 8 }}>
          {props.totalItems} items
        </span>
        <span
          className={classNames(
            "pagination-links",
            styles.TableNav_PaginationLinks,
          )}>
          <Button
            variant="secondary"
            disabled={props.currentPage === 1}
            onClick={async () => await props.setCurrentPage(1)}
            content="«"
          />
          <Button
            variant="secondary"
            disabled={props.currentPage === 1}
            onClick={async () =>
              await props.setCurrentPage(props.currentPage - 1)
            }
            content="‹"
          />
          <span className="paging-input">
            <label
              htmlFor="current-page-selector"
              className="screen-reader-text">
              Current Page
            </label>
            <input
              className="current-page"
              id="current-page-selector"
              type="text"
              name="paged"
              value={currentPageInput}
              onChange={(e) => {
                if (!isNaN(parseInt(e.target.value)) || e.target.value === "") {
                  setCurrentPageInput(e.target.value);
                }
              }}
              onKeyPress={async (e) => {
                if (e.key === "Enter") {
                  await props.setCurrentPage(parseInt(currentPageInput));
                }
              }}
              size={1}
              aria-describedby="table-paging"
            />
            <span className="tablenav-paging-text">
              {" "}
              of <span className="total-pages">{props.totalPages}</span>
            </span>
          </span>
          <Button
            variant="secondary"
            disabled={props.currentPage === props.totalPages}
            onClick={async () =>
              await props.setCurrentPage(props.currentPage + 1)
            }
            content="›"
          />
          <Button
            variant="secondary"
            disabled={props.currentPage === props.totalPages}
            onClick={async () => await props.setCurrentPage(props.totalPages)}
            content="»"
          />
        </span>
      </div>
    </div>
  );
};

export default TableNav;
