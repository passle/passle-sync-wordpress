import { Fragment } from "react";
import Button from "_Components/Atoms/LoadingButton/LoadingButton";
import { SyncState } from "_API/Enums/SyncState";
import RenderDate from "_Utils/date";
import "./Post.scss";
import { FeaturedItemType } from "_API/Types/FeaturedItemType";
import FeaturedItem from "_Components/Atoms/FeaturedItem/FeaturedItem";

export type PostProps = {
  id: string;
  title: string;
  excerpt: string;
  postUrl: string;
  featuredItem: FeaturedItemType;
  publishedDate: string;
  authorNames: string[];
  syncState?: SyncState;
  syncPost?: (passlePostId: string, callback: () => void) => void;
};

const Post = (props: PostProps) => {
  const hasSynced = props.syncState === SyncState.Synced;
  const isSyncing = props.syncState === SyncState.Syncing;

  return (
    <div className="post">
      <div className="post__ync-status">
        {!hasSynced && (
          <Button
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

      <div className="post__body">
        <div className="post__date">{RenderDate(props.publishedDate)}</div>
        <div className="post__authors">
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
        <div className="post__image">
          <FeaturedItem {...props.featuredItem} />
        </div>
        <div className="post__title">
          <a
            className="post__link"
            href={props.postUrl}
            target="_blank"
            rel="noreferrer"
          >
            {props.title}
          </a>
        </div>
        <div
          className="post__excerpt"
          dangerouslySetInnerHTML={{ __html: props.excerpt }}
        ></div>
      </div>
    </div>
  );
};

export default Post;
