import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";
import { FeaturedItemType } from "_API/Types/FeaturedItemType";

export type FeaturedItemProps = FeaturedItemType;

const FeaturedItem = (props: FeaturedItemProps) => {
  return (
    <>
      {props.variant === FeaturedItemVariant.Html ? (
        <div
          className="featured-image"
          dangerouslySetInnerHTML={{ __html: props.data }}
        ></div>
      ) : (
        <img src={props.data} />
      )}
    </>
  );
};

export default FeaturedItem;
