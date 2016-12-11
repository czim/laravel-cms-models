# List Filter Strategy: Basic Split String

Simple string filter that searches for loosy, split `%search% AND %term%` matches.

Shows a simple text input. The text entered will be searched for as separate words in the targets.

The search is loosy, so if the full searched text appears anywhere in the targets, the record is a match.

As an example, the input `'some string should match'` would be searched for as separate words combined by AND: 
THe record is a match only if its target matches `%some%` AND `%string%` AND `%should%` AND `%match%`.
