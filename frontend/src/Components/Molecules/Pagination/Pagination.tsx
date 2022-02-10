import { useEffect, useState, ReactNode } from "react";
import ReactPaginate from "react-paginate";
import { PasslePost } from "_API/Types/PasslePost";
import { WordpressPost } from "_API/Types/WordpressPost";
import "./Pagination.scss";

type Post = PasslePost | WordpressPost;

export type PaginatedItemsProps<T extends Post> = {
  items: T[];
  renderItem: (post: T) => ReactNode;
};

const PaginatedItems = <T extends Post>(props: PaginatedItemsProps<T>) => {
  const [pageItems, setPageItems] = useState<T[]>([]);
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
