import {RenderDate} from "../../../../_utils/date";
import "./SyncedPost.css";

function SyncedPost({ post }) {

  const authorString = Object.prototype.toString.call(post.post_authors) === '[object Array]'
    ? post.post_authors.join(", ")
    : post.post_authors;

  return (
    <div className="synced-post">
      <div className="post-image">
        {post.post_image_html && 
          <div className="featured-image" dangerouslySetInnerHTML={{__html: post.post_image_html}}></div>}
        {!post.post_image_html && <img src={post.post_image} />}
      </div>
      <div className="post-body">
        <div className="post-date">
          {RenderDate(post.post_date_gmt)}
        </div>
        <div className="post-authors">
          By{' '}
          {authorString}
        </div>
        <div className="post-title">
          <a className="post-link" href={post.guid} target="_blank" rel="noreferrer">
            {post.post_title}
          </a>
        </div>
        <div className="post-excerpt" dangerouslySetInnerHTML={{__html: post.post_preview}}></div>
      </div>
    </div>
  );
}

export default SyncedPost;
