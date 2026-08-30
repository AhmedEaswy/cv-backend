import fs from 'fs'

const { cssByFile } = JSON.parse(fs.readFileSync('scripts/scoped-styles-output.json', 'utf8'))
const tailwind = JSON.parse(fs.readFileSync('scripts/tailwind-long-classes.json', 'utf8'))

let md = `# Scoped Style Blocks Report\n\n**Total files:** ${Object.keys(cssByFile).length}\n\n`

md += `## Long inline Tailwind class strings (>=60 chars, >=5 utilities)\n\n`
for (const t of tailwind) {
  md += `- \`${t.file}\` (${t.len} chars, ${t.utilities} utilities, scoped=${t.hasScoped})\n  \`${t.sample}\`\n\n`
}

md += `## Full scoped CSS by file\n\n`
for (const [file, css] of Object.entries(cssByFile).sort(([a], [b]) => a.localeCompare(b))) {
  md += `### ${file}\n\n\`\`\`css\n${css}\n\`\`\`\n\n`
}

fs.writeFileSync('scripts/scoped-styles-report.md', md, 'utf8')
console.log('Report length:', md.length)
