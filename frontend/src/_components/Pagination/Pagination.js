import { useEffect, useState } from 'react';
import ReactPaginate from 'react-paginate';
import "./Pagination.css";

function PaginatedItems({ items, renderItem }) {
    const [pageItems, setPageItems] = useState([]);
    const [pageOffset, setPageOffset] = useState(0);
    
    const itemsPerPage = 10;
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
        {pageItems.map((post) => renderItem(post))}
        <ReactPaginate
          breakLabel="..."
          nextLabel="next >"
          onPageChange={handlePageClick}
          pageRangeDisplayed={5}
          pageCount={pageCount}
          previousLabel="< previous"
          renderOnZeroPageCount={null}
          className='pagination'
        />
      </>
    );
  }

  export default PaginatedItems;