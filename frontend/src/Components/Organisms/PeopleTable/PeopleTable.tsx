import { useContext, useMemo, useState } from "react";
import { PersonDataContext } from "_Contexts/PassleDataContext";
import Button from "_Components/Atoms/Button/Button";
import { FeaturedItemVariant } from "_API/Enums/FeaturedItemVariant";
import Table from "_Components/Molecules/Table/Table";
import FeaturedItem from "_Components/Atoms/FeaturedItem/FeaturedItem";
import {
  deleteAllPeople,
  deleteManyPeople,
  refreshAllPeople,
  syncAllPeople,
  syncManyPeople,
} from "_Services/SyncService";
import Badge from "_Components/Atoms/Badge/Badge";

const PeopleTable = () => {
  const { personData, refreshPeopleLists, setCurrentPage } =
    useContext(PersonDataContext);

  const [working, setWorking] = useState(false);

  const [selectedPeople, setSelectedPeople] = useState<string[]>([]);

  const allSelectedPeopleAreSynced = useMemo(
    () =>
      personData.data
        .filter((person) => selectedPeople.includes(person.shortcode))
        .every((person) => person.synced),
    [selectedPeople, personData],
  );

  const refreshList = async () => {
    await refreshAllPeople();
  };

  const syncAll = async () => {
    await syncAllPeople();
  };

  const syncSelected = async () => {
    await syncManyPeople({
      shortcodes: selectedPeople,
    });
  };

  const deleteAll = async () => {
    await deleteAllPeople();
  };

  const deleteSelected = async () => {
    await deleteManyPeople({
      shortcodes: selectedPeople,
    });
  };

  const doWork = async (fn: () => Promise<void>, cb: () => void) => {
    try {
      setWorking(true);

      await fn();
      await refreshPeopleLists();

      setWorking(false);
      setSelectedPeople([]);
      cb();
    } catch (e) {
      setWorking(false);
      cb();

      alert("Oops, something went wrong. Please try again.");
    }
  };

  const htmlDecode = (html: string) => {
    const doc = new DOMParser().parseFromString(html, "text/html");
    return doc.documentElement.textContent;
  };

  return (
    <div>
      <Table
        currentPage={personData.current_page}
        itemsPerPage={personData.items_per_page}
        totalItems={personData.total_items}
        totalPages={personData.total_pages}
        setCurrentPage={setCurrentPage}
        ActionsLeft={
          <>
            <Button
              variant="secondary"
              text="Refresh People"
              loadingText="Refreshing People..."
              disabled={working}
              onClick={(cb) => doWork(refreshList, cb)}
            />
            {selectedPeople.length ? (
              <Button
                variant="secondary"
                text="Sync Selected People"
                loadingText="Syncing People..."
                disabled={working}
                onClick={(cb) => doWork(syncSelected, cb)}
              />
            ) : (
              <Button
                variant="secondary"
                text="Sync All People"
                loadingText="Syncing People..."
                disabled={working}
                onClick={(cb) => doWork(syncAll, cb)}
              />
            )}
          </>
        }
        ActionsRight={
          <>
            {selectedPeople.length ? (
              <Button
                variant="secondary"
                text="Delete Selected People"
                loadingText="Deleting People..."
                disabled={!allSelectedPeopleAreSynced || working}
                onClick={(cb) => doWork(deleteSelected, cb)}
              />
            ) : (
              <Button
                variant="secondary"
                text="Delete All Synced People"
                loadingText="Deleting People..."
                disabled={!personData.data.length || working}
                onClick={(cb) => doWork(deleteAll, cb)}
              />
            )}
          </>
        }
        Head={
          <>
            <td id="cb" className="manage-column column-cb check-column">
              <input
                id="cb-select-all-1"
                type="checkbox"
                checked={selectedPeople.length === personData.data.length}
                onChange={(e) =>
                  setSelectedPeople(
                    e.target.checked
                      ? personData.data.map((x) => x.shortcode)
                      : [],
                  )
                }
              />
            </td>
            <th>Name</th>
            <th>Role</th>
            <th>Description</th>
            <th style={{ width: 100 }}>Synced</th>
          </>
        }
        Body={
          personData.data.length ? (
            personData.data.map((person) => {
              const description = useMemo(
                () => htmlDecode(person.description),
                [person.description],
              );

              return (
                <tr key={person.shortcode}>
                  <th scope="row" className="check-column">
                    <input
                      id="cb-select-1"
                      type="checkbox"
                      value={person.shortcode}
                      checked={selectedPeople.includes(person.shortcode)}
                      onChange={(e) =>
                        setSelectedPeople((state) =>
                          e.target.checked
                            ? [...state, person.shortcode]
                            : state.filter((x) => x !== person.shortcode),
                        )
                      }
                    />
                  </th>
                  <td style={{ display: "flex", alignItems: "flex-start" }}>
                    <FeaturedItem
                      variant={FeaturedItemVariant.Url}
                      data={
                        person.avatarUrl ||
                        "https://images.passle.net/200x200/assets/images/no_avatar.png"
                      }
                      circle={true}
                    />
                    {person.synced ? (
                      <a href={person.profileUrl} style={{ marginLeft: 12 }}>
                        {person.name}
                      </a>
                    ) : (
                      <div style={{ marginLeft: 12 }}>{person.name}</div>
                    )}
                  </td>
                  <td>{person.role || "—"}</td>
                  <td
                    dangerouslySetInnerHTML={{
                      __html: description || "—",
                    }}
                  />
                  <td>
                    <Badge
                      variant={person.synced ? "success" : "warning"}
                      text={person.synced ? "Synced" : "Unsynced"}
                    />
                  </td>
                </tr>
              );
            })
          ) : (
            <tr className="no-items">
              <td colSpan={4}>No people found.</td>
            </tr>
          )
        }
      />
    </div>
  );
};

export default PeopleTable;
