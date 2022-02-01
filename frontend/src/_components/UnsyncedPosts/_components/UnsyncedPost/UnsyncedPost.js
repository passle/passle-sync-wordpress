import LoadingButton from "../../../../_components/LoadingButton";
import {RenderDate} from "../../../../_utils/date";
import "./UnsyncedPost.css";

function UnsyncedPost({ post, syncPost, syncState }) {
  const hasSynced = syncState === "synced";
  const isSyncing = syncState === "syncing";

  return (
    <div className="unsynced-post">
      <div className="sync-status">
        {!hasSynced && (
          <LoadingButton
            text={"Sync post"}
            callback={(finishLoadingCallback) =>
              syncPost(post, finishLoadingCallback)
            }
            loadingText={"Syncing..."}
          />
        )}

        {hasSynced && <p>Synced</p>}
        {isSyncing && <p>Syncing...</p>}
      </div>

      <div className="post-body">
        <div className="post-date">
          {RenderDate(post.PublishedDate)}
        </div>
        <div className="post-authors">
          <span>
            By{' '}
            {post.Authors.map((author, ii) => (
              <>
                <a href={author.ProfileUrl} target="_blank" rel="nofollow noopener">{author.Name}</a>
                {ii < post.Authors.length - 1 ? ', ' : ''}
              </>
            ))}
          </span>
        </div>
        <div className="post-image">
          <img src={post.ImageUrl} />
        </div>
        <div className="post-title">
          <a className="post-link" href={post.PostUrl} target="_blank" rel="noreferrer">
            {post.PostTitle}
          </a>
        </div>
        <div className="post-excerpt" dangerouslySetInnerHTML={{__html: post.ContentTextSnippet}}></div>
      </div>
    </div>
  );
}

export default UnsyncedPost;
