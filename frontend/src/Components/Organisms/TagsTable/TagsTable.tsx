import { useEffect, useState } from "react";
import { Post } from "_API/Types/Post";
import { Tag } from "_API/Types/Tag";
import Table from "_Components/Molecules/Table/Table";
import { getTagData } from "_Services/SyncService";

const TagsTable = () => {
  const [tags, setTags] = useState<Tag[]>([]);
  const [loaded, setLoaded] = useState(false);
  
  useEffect(() => {
    const loadTagData = async () => {
      let tagViewModels = await getTagData();
      setTags(tagViewModels);
    };
    loadTagData();
  }, []);

  useEffect(() => {
    if (tags) setLoaded(true);
  }, [tags]);

  const RenderTagRow = (tag: Tag) => (
    <>
      <td style={{ display: "flex", alignItems: "flex-start" }}>{tag.name}</td>
      <td>{tag.nonPassleCount}</td>
      <td>{tag.syncedPassleCount}</td>
      <td>{tag.unsyncedPassleCount}</td>
    </>
  );

  return (
    <>
    {loaded && 
      <Table
        itemsPerPage={tags.length}
        totalItems={tags.length}
        Head={
          <>
            <th>Name</th>
            <th style={{ width: 150 }}>Non-Passle Posts</th>
            <th style={{ width: 150 }}>Synced Passle Posts</th>
            <th style={{ width: 100 }}>Unsynced Passle Posts</th>
          </>
        }
        Body={
          tags.length ? (
            tags.map((tag) => <tr key={tag.name}>{RenderTagRow(tag)}</tr>)
          ) : (
            <tr className="no-items">
              <td colSpan={4}>No tags found.</td>
            </tr>
          )
        }
      />
    }
    </>
  );
};

export default TagsTable;
