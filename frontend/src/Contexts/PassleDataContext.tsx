import { createContext, useState, ReactNode, useEffect, useMemo } from "react";
import { PaginatedResponse } from "_API/Types/PaginatedResponse";
import { Person } from "_API/Types/Person";
import { Post } from "_API/Types/Post";
import { getAllPosts } from "_Services/SyncService";
import { getAllPeople } from "_Services/SyncService";

type PostDataContextType = {
  postData?: PaginatedResponse<Post>;
  setCurrentPage: (page: number) => Promise<void>;
  setItemsPerPage: (count: number) => void;
  refreshPostLists: (page?: number, perPage?: number) => Promise<void>;
};

export const PostDataContext = createContext<PostDataContextType>({
  postData: null,
  setCurrentPage: async () => {},
  setItemsPerPage: () => {},
  refreshPostLists: async () => {},
});


type PersonDataContextType = {
  personData?: PaginatedResponse<Person>;
  setCurrentPage: (page: number) => Promise<void>;
  setItemsPerPage: (count: number) => void;
  refreshPeopleLists: (page?: number, perPage?: number) => Promise<void>;
};

export const PersonDataContext = createContext<PersonDataContextType>({
  personData: null,
  setCurrentPage: async () => {},
  setItemsPerPage: () => {},
  refreshPeopleLists: async () => {},
});

export type PassleDataContextProviderProps = {
  children?: ReactNode;
};

export const PassleDataContextProvider = (
  props: PassleDataContextProviderProps,
) => {
  const [postData, setPostData] = useState<PaginatedResponse<Post>>();
  const [personData, setPersonData] = useState<PaginatedResponse<Person>>();

  const setCurrentPostPage = async (page: number) =>
    await refreshData(page, postData.items_per_page);

  const setPostItemsPerPage = async (count: number) =>
    await refreshData(postData.current_page, count);
    
  const setCurrentPeoplePage = async (page: number) =>
  await refreshData(page, personData.items_per_page);

const setPeopleItemsPerPage = async (count: number) =>
  await refreshData(personData.current_page, count);

  const refreshPostLists = async (
    currentPage: number = 1,
    itemsPerPage: number = 20,
  ) => {
    const response = await getAllPosts({
      currentPage,
      itemsPerPage,
    });

    setPostData(response);
  };

  const refreshPeopleLists = async (
    currentPage: number = 1,
    itemsPerPage: number = 20,
  ) => {
    const response = await getAllPeople({
      currentPage,
      itemsPerPage,
    });

    setPersonData(response);
  };

  const refreshData = async (
    currentPage: number = 1,
    itemsPerPage: number = 20,
  ) => {
    await refreshPostLists(currentPage, itemsPerPage);
    await refreshPeopleLists(currentPage, itemsPerPage);
  }

  useEffect(() => {
    (async () => {
      await refreshData();
    })();
  }, []);

  return (
    <PostDataContext.Provider
      value={{
        postData: postData,
        setCurrentPage: setCurrentPostPage,
        setItemsPerPage: setPostItemsPerPage,
        refreshPostLists,
      }}>
        <PersonDataContext.Provider
      value={{
        personData: personData,
        setCurrentPage: setCurrentPeoplePage,
        setItemsPerPage: setPeopleItemsPerPage,
        refreshPeopleLists,
      }}>
        {props.children}
      </PersonDataContext.Provider>
    </PostDataContext.Provider>
  );
};
