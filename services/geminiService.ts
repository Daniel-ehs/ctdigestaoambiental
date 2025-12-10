import { GoogleGenAI } from "@google/genai";

// Initialize Gemini Client
const apiKey = process.env.API_KEY || '';
const ai = new GoogleGenAI({ apiKey });

export const generateInsights = async (context: string, moduleName: string): Promise<string> => {
  try {
    if (!apiKey) return "Chave de API não configurada. Insights indisponíveis.";

    const model = 'gemini-2.5-flash';
    const prompt = `
      Você é um especialista ambiental da CTDI do Brasil.
      Analise os seguintes dados JSON para o módulo ${moduleName}.
      Forneça 3 insights concisos e de alto impacto focando em eficiência, oportunidades de economia e anomalias.
      Mantenha um tom profissional e breve. Use marcadores (bullet points).
      Responda EXCLUSIVAMENTE em Português do Brasil (pt-BR).
      
      Dados:
      ${context}
    `;

    const response = await ai.models.generateContent({
      model,
      contents: prompt,
    });

    return response.text || "Nenhum insight gerado.";
  } catch (error) {
    console.error("Gemini Insight Error:", error);
    return "Não foi possível gerar insights no momento.";
  }
};

export const parseNaturalLanguageImport = async (text: string, type: 'electricity' | 'water' | 'waste'): Promise<string> => {
    try {
        if (!apiKey) return "[]";

        const schemaDescription = type === 'electricity' 
            ? `[{ "date": "YYYY-MM-DD", "unit": "Galpão X", "cpflKwh": number, "cpflCost": number, "floraKwh": number, "floraCost": number, "floraSavings": number }]`
            : type === 'water'
            ? `[{ "date": "YYYY-MM-DD", "unit": "Galpão X", "volume": number, "cost": number }]`
            : `[{ "date": "YYYY-MM-DD", "type": "string", "category": "Reciclável" | "Não Reciclável", "weight": number, "financial": number, "pricePerKg": number }]`;

        const prompt = `
            Parse the following raw text data into a valid JSON array matching this schema: ${schemaDescription}.
            The input data might be in Portuguese or English.
            Return ONLY the JSON array. No markdown, no explanations.
            Assume today's year if missing.
            
            Raw Data:
            ${text}
        `;

        const response = await ai.models.generateContent({
            model: 'gemini-2.5-flash',
            contents: prompt,
            config: {
                responseMimeType: 'application/json'
            }
        });

        return response.text;
    } catch (error) {
        console.error("Gemini Parse Error:", error);
        return "[]";
    }
}