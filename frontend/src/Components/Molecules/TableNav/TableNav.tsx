import { ReactNode, useContext, useEffect, useState } from "react";
import Button from "_Components/Atoms/Button/Button";
import { PostDataContext } from "_Contexts/PostDataContext";
import classNames from "_Utils/classNames";
import styles from "./TableNav.module.scss";

export type TableNavProps = {
  currentPage: number;
  itemsPerPage: number;
  totalItems: number;
  totalPages: number;
  ActionsLeft: ReactNode;
  ActionsRight: ReactNode;
};

const TableNav = (props: TableNavProps) => {
  const { postData, setCurrentPage, setItemsPerPage } =
    useContext(PostDataContext);

  const [currentPageInput, setCurrentPageInput] = useState("");

  useEffect(() => {
    setCurrentPageInput(postData.current_page.toString());
  }, [postData]);

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
            disabled={postData.current_page === 1}
            onClick={async () => await setCurrentPage(1)}
            text="«"
          />
          <Button
            variant="secondary"
            disabled={postData.current_page === 1}
            onClick={async () =>
              await setCurrentPage(postData.current_page - 1)
            }
            text="‹"
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
                  await setCurrentPage(parseInt(currentPageInput));
                }
              }}
              size={1}
              aria-describedby="table-paging"
            />
            <span className="tablenav-paging-text">
              {" "}
              of <span className="total-pages">{postData.total_pages}</span>
            </span>
          </span>
          <Button
            variant="secondary"
            disabled={postData.current_page === postData.total_pages}
            onClick={async () =>
              await setCurrentPage(postData.current_page + 1)
            }
            text="›"
          />
          <Button
            variant="secondary"
            disabled={postData.current_page === postData.total_pages}
            onClick={async () => await setCurrentPage(postData.total_pages)}
            text="»"
          />
        </span>
      </div>
    </div>
  );
};

export default TableNav;
