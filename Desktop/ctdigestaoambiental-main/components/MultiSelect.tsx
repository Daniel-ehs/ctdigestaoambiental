
import React, { useState, useRef, useEffect } from 'react';
import { ChevronDown, Check, X } from 'lucide-react';

interface MultiSelectProps {
  options: string[];
  selected: string[];
  onChange: (selected: string[]) => void;
  label: string;
  placeholder?: string;
}

const MultiSelect: React.FC<MultiSelectProps> = ({ options, selected, onChange, label, placeholder = "Todas" }) => {
  const [isOpen, setIsOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const toggleOption = (option: string) => {
    const isSelected = selected.includes(option);
    if (isSelected) {
      onChange(selected.filter(item => item !== option));
    } else {
      onChange([...selected, option]);
    }
  };

  const clearAll = (e: React.MouseEvent) => {
    e.stopPropagation();
    onChange([]);
    setIsOpen(false);
  };

  return (
    <div className="relative w-full md:w-64" ref={containerRef}>
      <label className="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5 ml-1">
        {label}
      </label>
      <div 
        onClick={() => setIsOpen(!isOpen)}
        className="flex items-center justify-between px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-ctdi-blue dark:hover:border-ctdi-blue transition-all shadow-sm"
      >
        <div className="flex flex-wrap gap-1 items-center overflow-hidden">
          {selected.length === 0 ? (
            <span className="text-sm text-slate-400 font-medium">{placeholder}</span>
          ) : (
            <div className="flex items-center gap-1">
               <span className="text-sm font-bold text-ctdi-blue dark:text-blue-400">
                {selected.length} {selected.length === 1 ? 'Selecionada' : 'Selecionadas'}
               </span>
            </div>
          )}
        </div>
        <div className="flex items-center gap-2 text-slate-400">
          {selected.length > 0 && (
            <X 
              size={14} 
              className="hover:text-rose-500 transition-colors" 
              onClick={clearAll}
            />
          )}
          <ChevronDown size={18} className={`transition-transform duration-300 ${isOpen ? 'rotate-180 text-ctdi-blue' : ''}`} />
        </div>
      </div>

      {isOpen && (
        <div className="absolute top-full left-0 right-0 mt-2 p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl z-[60] animate-in fade-in zoom-in duration-200 origin-top">
          <div className="max-h-60 overflow-y-auto space-y-1 scrollbar-thin">
            <div 
              onClick={clearAll}
              className={`flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer transition-colors ${selected.length === 0 ? 'bg-blue-50 dark:bg-blue-900/20 text-ctdi-blue' : 'hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300'}`}
            >
              <span className="text-sm font-medium">Todas as Unidades</span>
              {selected.length === 0 && <Check size={16} />}
            </div>
            <div className="h-px bg-slate-100 dark:bg-slate-700 my-1 mx-2" />
            {options.map((option) => {
              const isSelected = selected.includes(option);
              return (
                <div 
                  key={option}
                  onClick={() => toggleOption(option)}
                  className={`flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer transition-colors ${isSelected ? 'bg-blue-50 dark:bg-blue-900/20 text-ctdi-blue' : 'hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300'}`}
                >
                  <span className="text-sm font-medium">{option}</span>
                  {isSelected && <Check size={16} />}
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
};

export default MultiSelect;
