import { Fragment } from "react";
import LoadingButton from "__components/Atoms/LoadingButton/LoadingButton";
import { PasslePost } from "__services/SyncService";
import RenderDate from "__utils/date";
import "./UnsyncedPost.scss";

export type UnsyncedPostProps = {
  post: PasslePost;
  syncPost: (post: PasslePost, callback: () => void) => void;
  syncState: string;
};

const UnsyncedPost = (props: UnsyncedPostProps) => {
  const hasSynced = props.syncState === "synced";
  const isSyncing = props.syncState === "syncing";

  const post = props.post;

  return (
    <div className="unsynced-post">
      <div className="sync-status">
        {!hasSynced && (
          <LoadingButton
            text={"Sync post"}
            callback={(finishLoadingCallback) =>
              props.syncPost(post, finishLoadingCallback)
            }
            loadingText={"Syncing..."}
          />
        )}

        {hasSynced && <p>Synced</p>}
        {isSyncing && <p>Syncing...</p>}
      </div>

      <div className="post-body">
        <div className="post-date">{RenderDate(post.PublishedDate)}</div>
        <div className="post-authors">
          <span>
            By{" "}
            {post.Authors.map((author, ii) => (
              <Fragment key={ii}>
                <a
                  href={author.ProfileUrl}
                  target="_blank"
                  rel="nofollow noopener"
                >
                  {author.Name}
                </a>
                {ii < post.Authors.length - 1 ? ", " : ""}
              </Fragment>
            ))}
          </span>
        </div>
        <div className="post-image">
          <img src={post.ImageUrl} />
        </div>
        <div className="post-title">
          <a
            className="post-link"
            href={post.PostUrl}
            target="_blank"
            rel="noreferrer"
          >
            {post.PostTitle}
          </a>
        </div>
        <div
          className="post-excerpt"
          dangerouslySetInnerHTML={{ __html: post.ContentTextSnippet }}
        ></div>
      </div>
    </div>
  );
};

export default UnsyncedPost;
