import { PostDataContext } from "_Contexts/PassleDataContext";
import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";
import FeaturedItem from "_Components/Atoms/FeaturedItem/FeaturedItem";
import Badge from "_Components/Atoms/Badge/Badge";
import DataTable from "_Components/Organisms/DataTable/DataTable";

const PostsTable = () => {
  return (
    <DataTable
      itemSingular="post"
      itemPlural="posts"
      context={PostDataContext}
      TableHeadings={
        <>
          <th>Title</th>
          <th>Excerpt</th>
          <th style={{ width: 150 }}>Authors</th>
          <th style={{ width: 150 }}>Published Date</th>
          <th style={{ width: 100 }}>Synced</th>
        </>
      }
      RenderItem={(item) => (
        <>
          <td style={{ display: "flex", alignItems: "flex-start" }}>
            <FeaturedItem
              variant={FeaturedItemVariant.Url}
              data={item.imageUrl}
            />
            {item.synced ? (
              <a href={item.postUrl} style={{ marginLeft: 12 }}>
                {item.title}
              </a>
            ) : (
              <div style={{ marginLeft: 12 }}>{item.title}</div>
            )}
          </td>
          {item.excerpt ? (
            <td dangerouslySetInnerHTML={{ __html: item.excerpt }} />
          ) : (
            <td>—</td>
          )}
          <td>{item.authors}</td>
          <td>{item.publishedDate}</td>
          <td>
            <Badge
              variant={item.synced ? "success" : "warning"}
              text={item.synced ? "Synced" : "Unsynced"}
            />
          </td>
        </>
      )}
    />
  );
};

export default PostsTable;
