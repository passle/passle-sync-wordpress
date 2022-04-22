import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";
import { FeaturedItemType } from "_API/Types/FeaturedItemType";
import classNames from "_Utils/classNames";
import styles from "./FeaturedItem.module.scss";

export type FeaturedItemProps = FeaturedItemType & {
  circle?: boolean;
};

const FeaturedItem = ({ circle = false, ...props }: FeaturedItemProps) => {
  return (
    <>
      {props.variant === FeaturedItemVariant.Html ? (
        <div dangerouslySetInnerHTML={{ __html: props.data }}></div>
      ) : (
        <img
          src={props.data}
          width={circle ? 50 : 70}
          height={50}
          className={classNames(
            styles.FeaturedItem,
            circle && styles.FeaturedItem___Circle,
          )}
        />
      )}
    </>
  );
};

export default FeaturedItem;
