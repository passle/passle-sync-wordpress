import { Fragment, ReactNode } from "react";
import FeaturedItem from "__components/Atoms/FeaturedItem/FeaturedItem";
import LoadingButton from "__components/Atoms/LoadingButton/LoadingButton";
import { SyncState } from "__types/Enums/SyncState";
import RenderDate from "__utils/date";
import "./Post.scss";

export type PostProps = {
  id: string;
  title: string;
  snippet: string;
  postUrl: string;
  featuredItem: ReactNode;
  publishedDate: string;
  authorNames: string[];
  syncState?: SyncState;
  syncPost?: (passlePostId: string, callback: () => void) => void;
};

const Post = (props: PostProps) => {
  const hasSynced = props.syncState === SyncState.Synced;
  const isSyncing = props.syncState === SyncState.Syncing;

  return (
    <div className="unsynced-post">
      <div className="sync-status">
        {!hasSynced && (
          <LoadingButton
            text={"Sync post"}
            callback={(finishLoadingCallback) =>
              props.syncPost(props.id, finishLoadingCallback)
            }
            loadingText={"Syncing..."}
          />
        )}

        {/* {hasSynced && <p>Synced</p>}
        {isSyncing && <p>Syncing...</p>} */}
      </div>

      <div className="post-body">
        <div className="post-date">{RenderDate(props.publishedDate)}</div>
        <div className="post-authors">
          <span>
            By{" "}
            {props.authorNames.map((name, ii) => (
              <Fragment key={ii}>
                {name}
                {ii < props.authorNames.length - 1 ? ", " : ""}
              </Fragment>
            ))}
          </span>
        </div>
        <div className="post-image">
          <props.featuredItem />
        </div>
        <div className="post-title">
          <a
            className="post-link"
            href={props.postUrl}
            target="_blank"
            rel="noreferrer"
          >
            {props.title}
          </a>
        </div>
        <div
          className="post-excerpt"
          dangerouslySetInnerHTML={{ __html: props.snippet }}
        ></div>
      </div>
    </div>
  );
};

export default Post;
