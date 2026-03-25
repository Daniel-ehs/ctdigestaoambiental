export const generateInsights = async (context: string, moduleName: string): Promise<string> => {
  try {
    const response = await fetch('/api/ai-insights', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ context, moduleName })
    });

    if (!response.ok) {
      if (response.status === 500) return "Erro no servidor de IA.";
      throw new Error('Falha na requisição');
    }

    const data = await response.json();
    return data.text;
  } catch (error) {
    console.error("Gemini Insight Error:", error);
    return "Não foi possível gerar insights no momento.";
  }
};

export const parseNaturalLanguageImport = async (text: string, type: 'electricity' | 'water' | 'waste'): Promise<string> => {
  try {
    const response = await fetch('/api/ai-parse', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ text, type })
    });

    if (!response.ok) return "[]";

    const data = await response.json();
    return data.text;
  } catch (error) {
    console.error("Gemini Parse Error:", error);
    return "[]";
  }
};