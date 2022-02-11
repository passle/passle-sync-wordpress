import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";
import { FeaturedItemType } from "_API/Types/FeaturedItemType";
import styles from "./FeaturedItem.module.scss";

export type FeaturedItemProps = FeaturedItemType;

const FeaturedItem = (props: FeaturedItemProps) => {
  return (
    <>
      {props.variant === FeaturedItemVariant.Html ? (
        <div
          className="featured-image"
          dangerouslySetInnerHTML={{ __html: props.data }}></div>
      ) : (
        <img
          src={props.data}
          width={50}
          height="auto"
          className={styles.FeaturedItem}
        />
      )}
    </>
  );
};

export default FeaturedItem;
