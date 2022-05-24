import { createContext, useState, ReactNode, useEffect } from "react";
import { PaginatedResponse } from "_API/Types/PaginatedResponse";
import { Person } from "_API/Types/Person";
import { Post } from "_API/Types/Post";
import { Syncable } from "_API/Types/Syncable";
import { getAll } from "_Services/SyncService";

export type PassleDataContextType = {
  loading: boolean;
  setLoading: (loading: boolean) => void;
};

export const PassleDataContext = createContext<PassleDataContextType>({
  loading: false,
  setLoading: () => {},
});

export type DataContextType<T extends Syncable> = {
  data?: PaginatedResponse<T>;
  setCurrentPage: (page: number) => Promise<void>;
  setItemsPerPage: (count: number) => void;
  refreshItems: (page?: number, perPage?: number) => Promise<void>;
};

export const PostDataContext = createContext<DataContextType<Post>>({
  data: null,
  setCurrentPage: async () => {},
  setItemsPerPage: () => {},
  refreshItems: async () => {},
});

export const PersonDataContext = createContext<DataContextType<Person>>({
  data: null,
  setCurrentPage: async () => {},
  setItemsPerPage: () => {},
  refreshItems: async () => {},
});

export type PassleDataContextProviderProps = {
  children?: ReactNode;
};

export const PassleDataContextProvider = (
  props: PassleDataContextProviderProps,
) => {
  const [loading, setLoading] = useState(false);

  const [postData, setPostData] = useState<PaginatedResponse<Post>>();
  const [personData, setPersonData] = useState<PaginatedResponse<Person>>();

  const setCurrentPostPage = async (page: number) =>
    await refreshPostData(page, postData.items_per_page);

  const setPostItemsPerPage = async (count: number) =>
    await refreshPostData(postData.current_page, count);

  const setCurrentPeoplePage = async (page: number) =>
    await refreshPersonData(page, personData.items_per_page);

  const setPeopleItemsPerPage = async (count: number) =>
    await refreshPersonData(personData.current_page, count);

  const refreshPostData = async (
    currentPage: number = 1,
    itemsPerPage: number = 20,
  ) => {
    const response = await getAll<Post>("posts", {
      currentPage,
      itemsPerPage,
    });

    setPostData(response);
  };

  const refreshPersonData = async (
    currentPage: number = 1,
    itemsPerPage: number = 20,
  ) => {
    const response = await getAll<Person>("people", {
      currentPage,
      itemsPerPage,
    });

    setPersonData(response);
  };

  useEffect(() => {
    (async () => {
      await refreshPostData();
      await refreshPersonData();
    })();
  }, []);

  return (
    <PassleDataContext.Provider value={{ loading, setLoading }}>
      <PostDataContext.Provider
        value={{
          data: postData,
          setCurrentPage: setCurrentPostPage,
          setItemsPerPage: setPostItemsPerPage,
          refreshItems: refreshPostData,
        }}>
        <PersonDataContext.Provider
          value={{
            data: personData,
            setCurrentPage: setCurrentPeoplePage,
            setItemsPerPage: setPeopleItemsPerPage,
            refreshItems: refreshPersonData,
          }}>
          {props.children}
        </PersonDataContext.Provider>
      </PostDataContext.Provider>
    </PassleDataContext.Provider>
  );
};
