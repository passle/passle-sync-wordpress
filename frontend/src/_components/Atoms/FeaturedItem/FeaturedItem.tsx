import { FeaturedItemType } from "__types/Enums/FeaturedItemType";

export type FeaturedItemProps = {
  type: FeaturedItemType;
  data: string;
};

const FeaturedItem = (props: FeaturedItemProps) => {
  return (
    <>
      {props.type === FeaturedItemType.HTML ? (
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
