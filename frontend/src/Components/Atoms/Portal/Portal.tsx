import { ReactNode, useLayoutEffect, useState } from "react";
import { createPortal } from "react-dom";

const createWrapperAndAppendToBody = (wrapperId: string) => {
  const wrapperElement = document.createElement("div");
  wrapperElement.setAttribute("id", wrapperId);
  document.body.appendChild(wrapperElement);
  return wrapperElement;
};

export type PortalProps = {
  children: ReactNode;
  wrapperId?: string;
};

const Portal = ({
  wrapperId = "passle-sync-portal-wrapper",
  children,
}: PortalProps) => {
  const [wrapperElement, setWrapperElement] = useState<HTMLElement>(null);

  useLayoutEffect(() => {
    let element = document.getElementById(wrapperId);
    let wrapperCreated = false;

    if (!element) {
      wrapperCreated = true;
      element = createWrapperAndAppendToBody(wrapperId);
    }

    setWrapperElement(element);

    return () => {
      if (wrapperCreated && element.parentNode) {
        element.parentNode.removeChild(element);
      }
    };
  }, [wrapperId]);

  if (wrapperElement === null) return null;

  return createPortal(children, wrapperElement);
};

export default Portal;
