import { useEffect, useState } from "react";
import ReactPaginate from "react-paginate";
import { PasslePost, WordpressPost } from "__services/SyncService";
import "./Pagination.scss";

export type PaginatedItemsProps = {
  items: (PasslePost | WordpressPost)[];
  renderItem: (post: PasslePost | WordpressPost) => any;
};

const PaginatedItems = (props: PaginatedItemsProps) => {
  const [pageItems, setPageItems] = useState([]);
  const [pageOffset, setPageOffset] = useState(0);

  const itemsPerPage = 10;
  const items = props.items;
  const pageCount = Math.ceil(items.length / itemsPerPage);

  useEffect(() => {
    const endOffset = (pageOffset + 1) * itemsPerPage;
    setPageItems(items.slice(pageOffset * itemsPerPage, endOffset));
  }, [pageOffset, items]);

  const handlePageClick = (event) => {
    setPageOffset(event.selected);
  };

  return (
    <>
      {pageItems.map((post) => props.renderItem(post))}

      <ReactPaginate
        breakLabel="..."
        nextLabel="next >"
        onPageChange={handlePageClick}
        pageRangeDisplayed={5}
        pageCount={pageCount}
        previousLabel="< previous"
        renderOnZeroPageCount={null}
        className="pagination"
      />
    </>
  );
};

export default PaginatedItems;
