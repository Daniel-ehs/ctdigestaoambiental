import React from 'react';
import { ArrowUp, ArrowDown, Minus } from 'lucide-react';

interface MetricCardProps {
  title: string;
  value: string;
  change?: number;
  subtext?: string;
  icon: React.ElementType;
  color?: 'blue' | 'green' | 'orange' | 'red' | 'slate' | 'violet';
}

export const MetricCard: React.FC<MetricCardProps> = ({ 
  title, 
  value, 
  change, 
  subtext,
  icon: Icon,
  color = "blue"
}) => {
  const isPositive = change && change > 0;
  const isNegative = change && change < 0;

  // Mapeamento de gradientes vibrantes estilo "High-End"
  const gradients = {
    blue: "from-blue-600 to-indigo-700 shadow-blue-200 dark:shadow-none",
    green: "from-emerald-500 to-green-600 shadow-emerald-200 dark:shadow-none",
    orange: "from-orange-500 to-amber-600 shadow-orange-200 dark:shadow-none",
    red: "from-rose-500 to-red-600 shadow-rose-200 dark:shadow-none",
    slate: "from-slate-600 to-slate-800 shadow-slate-200 dark:shadow-none",
    violet: "from-violet-600 to-purple-700 shadow-violet-200 dark:shadow-none"
  };

  const selectedGradient = gradients[color] || gradients.blue;

  return (
    <div className={`relative overflow-hidden rounded-2xl p-6 text-white shadow-lg bg-gradient-to-br ${selectedGradient} group transition-all duration-300 hover:scale-[1.02] hover:shadow-xl`}>
      
      {/* Background Watermark Icon */}
      <div className="absolute -right-6 -top-6 opacity-10 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12 pointer-events-none">
        <Icon size={140} />
      </div>

      <div className="relative z-10 flex flex-col h-full justify-between">
        <div>
          <div className="flex items-center gap-2 mb-2 opacity-90">
             <div className="p-1.5 rounded-lg bg-white/20 backdrop-blur-sm">
                <Icon size={18} />
             </div>
             <p className="text-xs font-bold uppercase tracking-wider">{title}</p>
          </div>
          <h3 className="text-3xl font-bold tracking-tight text-white drop-shadow-sm">{value}</h3>
        </div>
        
        {(change !== undefined || subtext) && (
          <div className="mt-4 flex items-center gap-3">
            {change !== undefined && (
              <div className={`
                flex items-center gap-1 px-2 py-1 rounded-md text-xs font-bold backdrop-blur-md
                ${isPositive ? 'bg-emerald-400/20 text-emerald-50' : isNegative ? 'bg-rose-400/20 text-rose-50' : 'bg-white/20 text-white'}
              `}>
                {isPositive ? <ArrowUp size={12} strokeWidth={3} /> : isNegative ? <ArrowDown size={12} strokeWidth={3} /> : <Minus size={12} strokeWidth={3} />}
                {Math.abs(change).toFixed(2)}%
              </div>
            )}
            {subtext && <span className="text-xs font-medium opacity-70 truncate max-w-[150px]" title={subtext}>{subtext}</span>}
          </div>
        )}
      </div>
    </div>
  );
};