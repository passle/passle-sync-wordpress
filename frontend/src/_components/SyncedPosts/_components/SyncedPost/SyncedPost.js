import {RenderDate} from "../../../../_utils/date";
import "./SyncedPost.css";

function SyncedPost({ post }) {
  return (
    <div className="synced-post">
      <div className="post-body">
        <div className="post-date">
          {RenderDate(post.post_date_gmt)}
        </div>
        <div className="post-authors">
          By{' '}
          {/* {post.post_authors.map((author) => {
            return <a href={author.profileLink} target="_blank" rel="nofollow noopener">{author.name}</a>
          }).join(", ")} */}
        </div>
      {/* <img src={post.imageUrl} /> */}
        <div className="post-title">
          <a className="post-link" href={post.guid} target="_blank" rel="noreferrer">
            {post.post_title}
          </a>
        </div>
        <div className="post-excerpt" dangerouslySetInnerHTML={{__html: post.post_content}}></div>
      </div>
    </div>
  );
}

export default SyncedPost;
