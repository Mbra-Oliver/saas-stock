"use client";
import { Bell, ChevronLeft, ChevronRight, Menu, Search, X } from "lucide-react";
import { useEffect, useState } from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "../../ui/dropdown-menu";
import GlobalInputSearch from "../../ui/global-input-search";
import { MENU } from "../constantes/menu-constant";

const ResponsiveLayout = ({ children }: { children: React.ReactNode }) => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
  const [isMobile, setIsMobile] = useState(false);

  // Détecter la taille de l'écran
  useEffect(() => {
    const checkScreenSize = () => {
      setIsMobile(window.innerWidth < 768);
      if (window.innerWidth < 768) {
        setSidebarCollapsed(false);
      }
    };

    checkScreenSize();
    window.addEventListener("resize", checkScreenSize);
    return () => window.removeEventListener("resize", checkScreenSize);
  }, []);

  // Fermer la sidebar sur mobile lors du clic à l'extérieur
  useEffect(() => {
    const handleClickOutside = (event: any) => {
      if (
        isMobile &&
        sidebarOpen &&
        event &&
        event.target &&
        !event.target.closest(".sidebar") &&
        !event.target.closest(".menu-btn")
      ) {
        setSidebarOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [isMobile, sidebarOpen]);

  return (
    <div className="flex h-screen bg-gray-50 overflow-hidden">
      {/* Overlay pour mobile */}
      {isMobile && sidebarOpen && (
        <div
          className={`fixed inset-0 bg-black z-40 transition-opacity duration-300 ${
            sidebarOpen
              ? "bg-opacity-50 opacity-100"
              : "bg-opacity-0 opacity-0 pointer-events-none"
          }`}
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={`
          sidebar fixed left-0 top-0 h-full bg-white  z-50 transition-all duration-300 ease-in-out
          ${
            isMobile
              ? `${sidebarOpen ? "translate-x-0" : "-translate-x-full"} w-64`
              : `${sidebarCollapsed ? "w-16" : "w-64"} translate-x-0`
          }
        `}
      >
        {/* Logo/Brand */}
        <div className="flex items-center justify-between p-4 border-b border-gray-200">
          <div
            className={`flex items-center space-x-3 ${
              sidebarCollapsed && !isMobile ? "justify-center" : ""
            }`}
          >
            {(!sidebarCollapsed || isMobile) && (
              <span className="font-bold text-xl text-gray-800">
                Solution Stock
              </span>
            )}
          </div>

          {/* Bouton collapse pour desktop */}
          {!isMobile && (
            <button
              onClick={() => setSidebarCollapsed(!sidebarCollapsed)}
              className="p-1 rounded-md hover:bg-gray-100 transition-colors"
            >
              {sidebarCollapsed ? (
                <ChevronRight className="w-5 h-5 text-gray-600" />
              ) : (
                <ChevronLeft className="w-5 h-5 text-gray-600" />
              )}
            </button>
          )}

          {/* Bouton fermeture pour mobile */}
          {isMobile && (
            <button
              onClick={() => setSidebarOpen(false)}
              className="p-1 rounded-md hover:bg-gray-100 transition-colors"
            >
              <X className="w-6 h-6 text-gray-600" />
            </button>
          )}
        </div>

        {/* Navigation */}
        <nav className="flex-1 px-3 py-4 space-y-1">
          {MENU.map((item, index) => {
            const Icon = item.icon;
            return (
              <a
                key={index}
                href={item.href}
                className={`
                  flex items-center px-3 py-3  text-sm font-medium transition-all duration-200
                  ${
                    item.active
                      ? "bg-blue-50 text-primary border-r-2 border-primary"
                      : "text-gray-700 hover:bg-gray-50 hover:text-gray-900"
                  }
                  ${sidebarCollapsed && !isMobile ? "justify-center px-2" : ""}
                `}
                title={sidebarCollapsed && !isMobile ? item.label : ""}
              >
                <Icon
                  className={`w-5 h-5 ${
                    !sidebarCollapsed || isMobile ? "mr-3" : ""
                  }`}
                />
                {(!sidebarCollapsed || isMobile) && <span>{item.label}</span>}
              </a>
            );
          })}
        </nav>
      </aside>

      {/* Contenu principal */}
      <div
        className={`flex-1 flex flex-col ${
          !isMobile ? (sidebarCollapsed ? "ml-16" : "ml-64") : "ml-0"
        } transition-all duration-300`}
      >
        {/* Header */}
        <header className="bg-white  border-b border-gray-200 sticky top-0 z-30">
          <div className="flex items-center justify-between px-4 py-3">
            {/* Bouton menu mobile */}
            <div className="flex items-center space-x-4">
              <button
                onClick={() => setSidebarOpen(!sidebarOpen)}
                className="menu-btn p-2 rounded-md hover:bg-gray-100 transition-colors md:hidden"
              >
                <Menu className="w-6 h-6 text-gray-700" />
              </button>

              {/* Barre de recherche */}
              <div className="relative hidden sm:block">
                <GlobalInputSearch />
              </div>
            </div>

            {/* Actions header */}
            <div className="flex items-center space-x-3">
              {/* Recherche mobile */}
              <button className="p-2 rounded-md hover:bg-gray-100 transition-colors sm:hidden">
                <Search className="w-5 h-5 text-gray-600" />
              </button>

              {/* Notifications */}
              <button className="relative p-2 rounded-md hover:bg-gray-100 transition-colors">
                <Bell className="w-5 h-5 text-gray-600" />
                <span className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center">
                  <span className="text-xs text-white font-medium">0</span>
                </span>
              </button>

              {/* Avatar utilisateur */}

              <DropdownMenu>
                <div className="w-8 h-8 bg-primary rounded-full flex items-center justify-center cursor-pointer hover:bg-primary transition-colors">
                  <span className="text-sm font-medium text-white">
                    <DropdownMenuTrigger>SS</DropdownMenuTrigger>
                  </span>
                </div>
                <DropdownMenuContent>
                  <DropdownMenuLabel>My Account</DropdownMenuLabel>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem>Profile</DropdownMenuItem>
                  <DropdownMenuItem>Billing</DropdownMenuItem>
                  <DropdownMenuItem>Team</DropdownMenuItem>
                  <DropdownMenuItem>Subscription</DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>
        </header>

        {/* Zone de contenu */}
        <main className="flex-1 overflow-y-auto">
          <div className="p-4 sm:p-6">{children}</div>
        </main>
      </div>
    </div>
  );
};

export default ResponsiveLayout;
