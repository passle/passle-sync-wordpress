import { Fragment } from "react";
import Button from "_Components/Atoms/Button/Button";
import { SyncState } from "_API/Enums/SyncState";
import renderDate from "_Utils/renderDate";
import styles from "./Post.module.scss";
import { FeaturedItemType } from "_API/Types/FeaturedItemType";
import FeaturedItem from "_Components/Atoms/FeaturedItem/FeaturedItem";
import classNames from "_Utils/classNames";

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
  const hasSynced = (props.syncState ?? SyncState.Synced) === SyncState.Synced;

  return (
    <div className={styles.Post}>
      <div className={styles.Post_SyncStatus}>
        {!hasSynced && (
          <Button
            content="Sync post"
            onClick={(cb) => props.syncPost(props.id, cb)}
            loadingContent="Syncing..."
          />
        )}
      </div>

      <div className={styles.Post_Body}>
        <div className={styles.Post_Date}>
          {renderDate(props.publishedDate)}
        </div>
        <div>
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
        <div
          className={classNames(
            styles.Post_Image,
            hasSynced && styles.Post_Image___NoSync,
          )}>
          <FeaturedItem {...props.featuredItem} />
        </div>
        <div className={styles.Post_Title}>
          <a href={props.postUrl} target="_blank" rel="noreferrer">
            {props.title}
          </a>
        </div>
        <div dangerouslySetInnerHTML={{ __html: props.excerpt }}></div>
      </div>
    </div>
  );
};

export default Post;
