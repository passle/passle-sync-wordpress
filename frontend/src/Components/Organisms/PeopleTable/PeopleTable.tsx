import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";
import FeaturedItem from "_Components/Atoms/FeaturedItem/FeaturedItem";
import Badge from "_Components/Atoms/Badge/Badge";
import DataTable from "_Components/Organisms/DataTable/DataTable";
import { PersonDataContext } from "_Contexts/PassleDataContext";

const PeopleTable = () => {
  const htmlDecode = (html: string) => {
    const doc = new DOMParser().parseFromString(html, "text/html");
    return doc.documentElement.textContent;
  };

  return (
    <DataTable
      itemSingular="person"
      itemPlural="people"
      context={PersonDataContext}
      TableHeadings={
        <>
          <th>Name</th>
          <th>Role</th>
          <th>Description</th>
          <th style={{ width: 100 }}>Synced</th>
        </>
      }
      RenderItem={(item) => {
        return (
          <>
            <td style={{ display: "flex", alignItems: "flex-start" }}>
              <FeaturedItem
                variant={FeaturedItemVariant.Url}
                data={
                  item.avatarUrl ||
                  "https://images.passle.net/200x200/assets/images/no_avatar.png"
                }
                circle={true}
              />
              {item.synced && item.profileUrl ? (
                <a href={item.profileUrl} style={{ marginLeft: 12 }}>
                  {item.name}
                </a>
              ) : (
                <div style={{ marginLeft: 12 }}>{item.name}</div>
              )}
            </td>
            <td>{item.role || "—"}</td>
            <td
              dangerouslySetInnerHTML={{
                __html: htmlDecode(item.description) || "—",
              }}
            />
            <td>
              <Badge
                variant={item.synced ? "success" : "warning"}
                text={item.synced ? "Synced" : "Unsynced"}
              />
            </td>
          </>
        );
      }}
    />
  );
};

export default PeopleTable;
